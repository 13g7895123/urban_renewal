/**
 * Voting API composables
 */
export const useVotingTopics = () => {
  const { get, post, put, patch, delete: del } = useApi()

  /**
   * Get all voting topics
   */
  const getVotingTopics = async (params = {}) => {
    return await get('/voting-topics', params)
  }

  /**
   * Get specific voting topic
   */
  const getVotingTopic = async (id) => {
    return await get(`/voting-topics/${id}`)
  }

  /**
   * Create new voting topic
   */
  const createVotingTopic = async (data) => {
    return await post('/voting-topics', data)
  }

  /**
   * Update voting topic
   */
  const updateVotingTopic = async (id, data) => {
    return await put(`/voting-topics/${id}`, data)
  }

  /**
   * Delete voting topic
   */
  const deleteVotingTopic = async (id) => {
    return await del(`/voting-topics/${id}`)
  }

  /**
   * Start voting for a topic
   */
  const startVoting = async (id) => {
    return await patch(`/voting-topics/${id}/start-voting`)
  }

  /**
   * Close voting for a topic
   */
  const closeVoting = async (id) => {
    return await patch(`/voting-topics/${id}/close-voting`)
  }

  /**
   * Get voting topics statistics
   */
  const getVotingTopicsStatistics = async () => {
    return await get('/voting-topics/statistics')
  }

  /**
   * Get upcoming voting topics
   */
  const getUpcomingVotingTopics = async () => {
    return await get('/voting-topics/upcoming')
  }

  return {
    getVotingTopics,
    getVotingTopic,
    createVotingTopic,
    updateVotingTopic,
    deleteVotingTopic,
    startVoting,
    closeVoting,
    getVotingTopicsStatistics,
    getUpcomingVotingTopics
  }
}

export const useVoting = () => {
  const { get, post, delete: del } = useApi()

  /**
   * Get all votes
   */
  const getVotes = async (params = {}) => {
    return await get('/voting', params)
  }

  /**
   * Cast a vote
   */
  const vote = async (data) => {
    return await post('/voting/vote', data)
  }

  /**
   * Cast multiple votes
   */
  const batchVote = async (data) => {
    return await post('/voting/batch-vote', data)
  }

  /**
   * Get user's vote for specific topic
   */
  const getMyVote = async (topicId) => {
    return await get(`/voting/my-vote/${topicId}`)
  }

  /**
   * Remove a vote
   */
  const removeVote = async (data) => {
    return await del('/voting/remove-vote', data)
  }

  /**
   * Get voting statistics for topic
   */
  const getVotingStatistics = async (topicId) => {
    return await get(`/voting/statistics/${topicId}`)
  }

  /**
   * Export voting results
   */
  const exportVotingResults = async (topicId) => {
    return await get(`/voting/export/${topicId}`)
  }

  /**
   * Get detailed voting results
   */
  const getDetailedResults = async (topicId) => {
    return await get(`/voting/detailed/${topicId}`)
  }

  /**
   * Recalculate area weights for a voting topic
   */
  const recalculateAreaWeights = async (topicId) => {
    return await post(`/voting/recalculate-weights/${topicId}`)
  }

  return {
    getVotes,
    vote,
    batchVote,
    getMyVote,
    removeVote,
    getVotingStatistics,
    exportVotingResults,
    getDetailedResults,
    recalculateAreaWeights
  }
}